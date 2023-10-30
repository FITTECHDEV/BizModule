<template>
    <div class="chat direct-chat-messages direct-chat-primary">
        <div class="left clearfix " v-for="(message, index) in messages" :key="index">            
            <div v-if="message.TicketID == data.Id">
                <div class="">
                     <div :class="['direct-chat-msg', (message.Role == 1) ? 'left' : 'right' ]">
                        <div class="direct-chat-infos clearfix">
                          <span :class="['direct-chat-name', (message.Role == 1) ? 'float-left' : 'float-right' ]">{{ message.user.full_name }}</span>
                          <span :class="['direct-chat-timestamp', (message.Role == 1) ? 'float-right' : 'float-left' ]">{{moment(message.CreatedOn).format("DD-MM-YYYY h:m:s A")}}</span>
                        </div>
                        <span class="direct-chat-img fas fa-lg fa-user"></span>
                        <div class="direct-chat-text">
                           <div v-html="message.Comment"></div>
                        </div>
                      </div>
                </div>
            </div>
            <div v-else-if="message.TicketID == null">
                <div class="">
                     <div :class="['direct-chat-msg', (user.Role == 1) ? 'left' : 'right' ]">
                        <div class="direct-chat-infos clearfix">
                          <span :class="['direct-chat-name', (user.Role == 1) ? 'float-left' : 'float-right' ]">{{ message.user.full_name }}</span>
                          <span :class="['direct-chat-timestamp', (user.Role == 1) ? 'float-right' : 'float-left' ]">{{moment(message.CreatedOn).format("YYYY-MM -DD h:m:s A")}}</span>
                        </div>
                        <span class="direct-chat-img fas fa-lg fa-user"></span>
                        <div class="direct-chat-text">
                           <div v-html="message.Comment"></div>
                        </div>
                      </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import moment from "moment";
export default {
    props: ["messages", "data", "user"],
    mounted() {
            /* console.log(this.messages);*/
        },
    data(){
        return {
            moment: moment
        }
    }
};
</script>
